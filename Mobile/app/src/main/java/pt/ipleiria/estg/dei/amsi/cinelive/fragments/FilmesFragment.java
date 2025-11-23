package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.content.Intent;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.SearchView;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.GridLayoutManager;

import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;

import java.util.Arrays;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.DetalhesFilmeActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.FilmesAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentFilmesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link FilmesFragment#newInstance} factory method to
 * create an instance of this fragment.
 */
public class FilmesFragment extends Fragment {
    private FragmentFilmesBinding binding;
    private SearchView searchView;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentFilmesBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setHasOptionsMenu(true);
    }

    @Override
    public void onCreateOptionsMenu(@NonNull Menu menu, @NonNull MenuInflater inflater) {
        super.onCreateOptionsMenu(menu, inflater);

        inflater.inflate(R.menu.menu_pesquisa, menu);

        // Obter item
        MenuItem itemPesquisa = menu.findItem(R.id.itemPesquisa);

        // Obter SearchView
        searchView = (SearchView) itemPesquisa.getActionView();
        searchView.setQueryHint(getString(R.string.pesquisar_filmes));
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        View.OnClickListener filterClickListener = v -> {
            // Atualiza estado checked (só um ativo)
            binding.btnEmExibicao.setChecked(v.getId() == R.id.btnEmExibicao);
            binding.btnKids.setChecked(v.getId() == R.id.btnKids);
            binding.btnBrevemente.setChecked(v.getId() == R.id.btnBrevemente);

            int id = v.getId();
            if (id == R.id.btnEmExibicao) {
                // TODO: DO SOMETHING
            }
            else if (id == R.id.btnKids) {
                // TODO: DO SOMETHING
            }
            else if (id == R.id.btnBrevemente) {
                // TODO: DO SOMETHING
            }
        };

        binding.btnEmExibicao.setOnClickListener(filterClickListener);
        binding.btnKids.setOnClickListener(filterClickListener);
        binding.btnBrevemente.setOnClickListener(filterClickListener);

        // Opcional: definir um default
        binding.btnEmExibicao.setChecked(true);


        // Grelha 2 colunas
        binding.rvFilmes.setLayoutManager(new GridLayoutManager(getContext(), 3));

        // Dados fake (temporário)
        List<Filme> listaFake = Arrays.asList(
                new Filme("Interstellar", R.drawable.poster_placeholder),
                new Filme("Dune: Part Two", R.drawable.poster_placeholder),
                new Filme("Inside Out 2", R.drawable.poster_placeholder),
                new Filme("Moana 2", R.drawable.poster_placeholder),
                new Filme("Oppenheimer", R.drawable.poster_placeholder),
                new Filme("Interstellar", R.drawable.poster_placeholder),
                new Filme("Dune: Part Two", R.drawable.poster_placeholder),
                new Filme("Inside Out 2", R.drawable.poster_placeholder),
                new Filme("Moana 2", R.drawable.poster_placeholder),
                new Filme("Oppenheimer", R.drawable.poster_placeholder),
                new Filme("Interstellar", R.drawable.poster_placeholder),
                new Filme("Dune: Part Two", R.drawable.poster_placeholder),
                new Filme("Inside Out 2", R.drawable.poster_placeholder),
                new Filme("Moana 2", R.drawable.poster_placeholder),
                new Filme("Oppenheimer", R.drawable.poster_placeholder)
        );

        // Adapter
        FilmesAdapter adapter = new FilmesAdapter(listaFake, filme -> {
            // Clicar no filme -> ir para detalhes
            startActivity(new Intent(getActivity(), DetalhesFilmeActivity.class));
        });

        binding.rvFilmes.setAdapter(adapter);

    }


    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}