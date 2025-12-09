package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.content.Intent;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.ConfiguracoesActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesCompraActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.MainActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.ComprasAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentComprasBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ComprasListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.ComprasManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class ComprasFragment extends Fragment {
    private FragmentComprasBinding binding;
    private ComprasManager comprasManager;
    private ComprasAdapter adapter;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Obter o compras manager
        comprasManager = ComprasManager.getInstance();
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentComprasBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        // Configurar layout da recycler-view
        binding.rvCompras.setLayoutManager(new LinearLayoutManager(getContext()));

        // Carregar compras
        loadCompras();

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);

            // Apenas limpar a cache de compras se tiver internet
            if (ConnectionUtils.hasInternet(requireContext())) comprasManager.clearCache();

            // Carregar compras
            loadCompras();
        });
    }

    private void loadCompras() {
        binding.mainFlipper.setDisplayedChild(0); // Main Loading
        updateToolbarTitle(R.string.nav_compras);

        // Obter estado da ligação à internet
        boolean hasInternet = ConnectionUtils.hasInternet(requireContext());

        // Obter compras (API ou cache)
        comprasManager.getCompras(requireContext(), new ComprasListener() {
            @Override
            public void onSuccess(List<Compra> compras) {
                setList(compras);
            }
            @Override
            public void onLocal(List<Compra> compras) {
                setList(compras);

                // Mudar texto da toolbar se as compras forem locais
                updateToolbarTitle(R.string.title_compras_locais);
            }
            @Override
            public void onEmpty() {
                showError(ErrorUtils.Type.EMPTY_COMPRAS);
            }
            @Override
            public void onError() {
                showError(hasInternet ? ErrorUtils.Type.API_ERROR : ErrorUtils.Type.NO_INTERNET);
            }
        });
    }

    private void setList(List<Compra> compras) {
        // Evitar crash ao sair do fragment
        if (binding == null || !isAdded()) return;
        binding.mainFlipper.setDisplayedChild(2); // Main Content

        // Se clicou numa compra --> ir para detalhes
        adapter = new ComprasAdapter(compras, compra -> {
            Intent intent = new Intent(getActivity(), DetalhesCompraActivity.class);
            intent.putExtra("compraId", compra.getId());
            startActivity(intent);
        });

        binding.rvCompras.setAdapter(adapter);
    }

    private void showError(ErrorUtils.Type type) {
        // Evitar crash ao sair do fragment
        if (binding == null || !isAdded()) return;
        binding.mainFlipper.setDisplayedChild(1); // Main Error
        ErrorUtils.showLayout(binding.mainError, type);

        // Action do botão
        binding.mainError.btnAction.setOnClickListener(v -> {
            switch (type) {
                case NO_INTERNET:
                    loadCompras();
                    break;
                case EMPTY_COMPRAS:
                    ((MainActivity)requireActivity()).navigateToFragment(R.id.navFilmes);
                    break;
                case API_ERROR:
                    startActivity(new Intent(requireContext(), ConfiguracoesActivity.class));
                    break;
            }
        });
    }

    private void updateToolbarTitle(int resId) {
        ((MainActivity)getActivity()).getSupportActionBar().setTitle(resId);
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}