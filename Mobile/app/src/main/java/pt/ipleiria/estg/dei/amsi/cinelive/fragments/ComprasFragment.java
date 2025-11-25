package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.content.Intent;
import android.os.Bundle;

import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import java.util.Arrays;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesCompraActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesFilmeActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.CinemasAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.ComprasAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentComprasBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link ComprasFragment} factory method to
 * create an instance of this fragment.
 */
public class ComprasFragment extends Fragment {
    private FragmentComprasBinding binding;
    private ComprasAdapter adapter;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentComprasBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        List<Compra> compras = Arrays.asList(
            new Compra(1, "Interstellar", "05/11/2025", "CineLive Leiria", "Confirmada", "10.00€", "07/11/2025", "10:00", "A5, A6, A7"),
            new Compra(2, "Interstellar", "05/11/2025", "CineLive Leiria", "Confirmada", "10.00€", "07/11/2025", "10:00", "A5, A6, A7"),
            new Compra(3, "Interstellar", "05/11/2025", "CineLive Leiria", "Confirmada", "10.00€", "07/11/2025", "10:00", "A5, A6, A7"),
            new Compra(4, "Interstellar", "05/11/2025", "CineLive Leiria", "Confirmada", "10.00€", "07/11/2025", "10:00", "A5, A6, A7")
        );

        adapter = new ComprasAdapter(
            compras, compra -> {
                Intent intent = new Intent(getActivity(), DetalhesCompraActivity.class);
                intent.putExtra("compra_id", compra.id);
                startActivity(intent);
            }
        );

        binding.rvCompras.setLayoutManager(new LinearLayoutManager(getContext()));
        binding.rvCompras.setAdapter(adapter);

    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}